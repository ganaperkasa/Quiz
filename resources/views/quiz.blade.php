<!DOCTYPE html>
<html>

<head>
    <title>Quiz Drag & Drop</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">


    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: linear-gradient(135deg, #f3f9ff, #e0f7fa);
            margin: 0;
            padding: 20px;
        }

        .container {
            display: flex;
            gap: 20px;
            justify-content: center;
        }

        .card {
            flex: 1;
            max-width: 350px;
            /* Lebih kecil */
            border: none;
            border-radius: 12px;
            padding: 15px;
            background: #ffffff;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s ease-in-out;
        }

        .card:hover {
            transform: translateY(-3px);
        }

        .card h3 {
            margin-top: 0;
            text-align: center;
            color: #0277bd;
            font-size: 1.2em;
        }

        .dropzone {
            border: 2px dashed #90caf9;
            padding: 10px;
            min-height: 40px;
            background: #f1f8ff;
            margin-bottom: 20px;
            border-radius: 8px;
            transition: background 0.2s ease;
        }

        .dropzone:hover {
            background: #e3f2fd;
        }

        .answer {
            background: #ffecb3;
            color: #6d4c41;
            padding: 8px;
            margin: 5px 0;
            cursor: grab;
            border-radius: 6px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            transition: background 0.2s ease;
        }

        .answer:hover {
            background: #ffe082;
        }

        .correct {
            background-color: #c8e6c9 !important;
            color: #256029;
        }

        .wrong {
            background-color: #ffcdd2 !important;
            color: #b71c1c;
        }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 9999;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
        }

        .modal-content {
            background: white;
            margin: 15% auto;
            padding: 20px;
            width: 280px;
            text-align: center;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
        }

        .modal-content h2 {
            margin-top: 0;
            color: #0277bd;
        }

        .close-btn {
            background: #2196F3;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
        }

        .close-btn:hover {
            background: #1976d2;
        }
    </style>
</head>

<body>
    <h1 style="text-align:center;">Quiz Drag & Drop</h1>

    <div class="container">

        <div class="card">
            <h3>Jawaban</h3>
            <div id="answers">
                @foreach ($answers as $ans)
                    <div class="answer" draggable="true">{{ $ans }}</div>
                @endforeach
            </div>
        </div>


        <div class="card">
            <h3>Pertanyaan</h3>
            <div id="quiz">
                @foreach ($questions as $q)
                    <div class="question" data-id="{{ $q->id }}">
                        <p><strong>{{ $q->question }}</strong></p>
                        <div class="dropzone"></div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div style="text-align:center;margin-top:20px;">
        <button class="btn btn-primary btn-md" id="submitBtn" >Cek Jawaban</button>
    </div>


    <div id="scoreModal" class="modal">
        <div class="modal-content">
            <h2 id="scoreText"></h2>
            <button class="close-btn" onclick="closeModal()">Tutup</button>
        </div>
    </div>

    <script>
        let answers = document.querySelectorAll('.answer');
        const dropzones = document.querySelectorAll('.dropzone');
        const answersContainer = document.getElementById('answers');

        function addDragEvents(ans) {
            ans.addEventListener('dragstart', e => {
                e.dataTransfer.setData('text/plain', ans.innerText);
            });
        }
        answers.forEach(ans => addDragEvents(ans));

        dropzones.forEach(zone => {
            zone.addEventListener('dragover', e => e.preventDefault());
            zone.addEventListener('drop', e => {
                e.preventDefault();
                const text = e.dataTransfer.getData('text/plain');
                if (zone.dataset.answer) return;
                zone.innerHTML = `<div class="answer">${text}</div>`;
                zone.dataset.answer = text;
                document.querySelectorAll('#answers .answer').forEach(a => {
                    if (a.innerText === text) a.remove();
                });
            });
        });

        document.getElementById('submitBtn').addEventListener('click', () => {
            const data = {};
            document.querySelectorAll('.question').forEach(q => {
                const id = q.dataset.id;
                const answer = q.querySelector('.dropzone').dataset.answer || '';
                data[id] = answer;
            });

            fetch('{{ route('quiz.check') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        answers: data
                    })
                })
                .then(res => res.json())
                .then(res => {
                    for (let qid in res.results) {
                        let qElem = document.querySelector(`.question[data-id="${qid}"] .dropzone`);
                        if (res.results[qid].isCorrect) {
                            qElem.classList.add('correct');
                            qElem.classList.remove('wrong');
                        } else {
                            qElem.classList.add('wrong');
                            qElem.classList.remove('correct');
                        }
                    }
                    showModal(res.score);
                });
        });

        function showModal(score) {
            document.getElementById('scoreText').innerText = `Score Anda: ${score}`;
            document.getElementById('scoreModal').style.display = 'block';
        }
        let initialAnswers = Array.from(document.querySelectorAll('.answer'));

        function closeModal() {
            document.getElementById('scoreModal').style.display = 'none';

            document.querySelectorAll('.dropzone').forEach(zone => {
                zone.classList.remove('correct', 'wrong');
                zone.innerHTML = ''; // kosongkan
                delete zone.dataset.answer;
            });

            let containerAwal = document.getElementById('answers');
            let shuffledAnswers = [...initialAnswers].sort(() => Math.random() - 0.5);

            containerAwal.innerHTML = '';
            shuffledAnswers.forEach(answer => {
                containerAwal.appendChild(answer);
            });
        }
    </script>
</body>

</html>
